//global variable for object to store repetitions
var graphs = {};
var warn = true;
var first = true;
function create_graph(id, labels, lines = 1) {
  //Plotly.plot(ID, [LINE DATA]);
  //create LINE DATA array
  var line_data = [];
  for (var i = 0; i < lines; i++) {
    line_data.push({y:[i], type: 'line', name: labels[i]});
  }
  //Plot graph
  Plotly.plot(id, line_data);
  //add graph to list
  graphs[id] = 0;
}
function prep_extend(input) {
  var data = {y: []}
  var number = []
  for (var i = 0; i < input.length; i++) {
    data["y"].push([input[i]]);
    number.push(i);
  }
  return [data, number]
}

function update_graph(id, input) {
  //digest data
  var data = prep_extend(input);
  //add data to graph
  Plotly.extendTraces(id, data[0], data[1]);
  //scroll plot when over 60
  graphs[id]++;
  if (graphs[id] > 60) {
    Plotly.relayout(id, {xaxis: {range: [graphs[id]-60, graphs[id]]}});
  }
}

function request_data(key, id) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      try {
        var data = JSON.parse(this.responseText);
      }
      catch (e) {
        error_modal(e, this.responseText);
        return 0;
      }

      for (var i = 0; i < id.length; i++) {
        //check if it wants to put the average in the graph
        if (data[id[i]].hasOwnProperty('average') && data[id[i]].average == true) {
          var avg = getAverage(data[id[i]].value);
          update_graph(id[i], [avg]);

          update_graph_raw(id[i], data[id[i]].value);

          //show graph
          if (first) {
            show_graph(id[i]);
          }
        }
        else {
          //check if it's a string or an object
          if (data[id[i]].value instanceof Array) {
            update_graph(id[i], data[id[i]].value);
          }
          else {
            update_graph(id[i], [data[id[i]].value]);
          }
        }
      }
      first = false;
    }
  };
  xhttp.open("GET", "/api.php?key="+key, true);
  xhttp.send();
}

function clear_graph(id, lines) {
  for (var x = 0; x < id.length; x++) {
    var line_data = [];
    for (var i = 0; i < lines[x]; i++) {
      line_data.push({y:[i], type: 'line'});
    }
    Plotly.newPlot(id[x], line_data);
    graphs[id[x]] = 0;
  }
}

function error_modal(error, message) {
  if (!warn) {
    console.error(error);
    console.error(message);
    return null;
  }
  // Get the modal
  var modal = document.getElementById("message_modal");
  var modal_content = document.getElementById("modal_content");
  modal_content.innerHTML = '<p style="padding:32px;background-color:tomato;border:solid 1px red;border-radius:24px;font-size:36px;">'+message+"</p><p>"+error+"</p>";
  modal.style.display = 'block';
}

function getAverage(data) {
  //check if the data has an actual length so we wont devide by zero
  //0 == false in JavaScript
  if (!data.length) {
    return 0;
  }
  var total = 0, avg = 0;
  for (var i = 0; i < data.length; i++) {
    //add all numbers in data to total
    total += Number(data[i]);
  }
  //devide total
  var avg = total / data.length;
  return avg;
}
function show_graph(id) {
  var parent = document.getElementById(id+'_raw');
  parent.parentElement.style.display = 'inline';
}
function update_graph_raw(id, data) {
  var parent = document.getElementById(id+'_raw');
  var tabledata = '';
  for(var i = 0;i < data.length;i++) {
    //create table cell
    tabledata += "<div><span>Sensor "+i+"</span><span>"+data[i]+"</span></div>";
  }
  parent.innerHTML = tabledata;
}
function create_container(id) {
  var container = '<div class="graph-container"><p id="'+id+'_title"><a href="#'+id+'">'+id+'</a></p><div id="'+id+'"></div></div><div class="raw-container"><p id="'+id+'_title_raw"><a href="#'+id+'_title_raw">'+id+' RAW</a></p><div id="'+id+'_raw"></div></div>';
  document.getElementById('main').innerHTML += container;
}
